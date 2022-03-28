def output_val(number):
    if number % 15 == 0:
        return "foobar"
    elif i % 5 == 0:
        return "bar"
    elif i % 3 == 0:
        return "foo"
    else:
        return number


for i in range(1, 101):
    if i != 100:
        delimiter = ', '
        ending = ''
    else:
        delimiter = ''
        ending = '\n'
    print(output_val(i), delimiter, sep='', end=ending)
